<?php

namespace BooksBundle\Controller;

use BooksBundle\Entity\Books;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Book controller.
 *
 */
class BooksController extends Controller
{
    /**
     * Lists all book entities.
     *
     */
    public function backindexAction()

    {
        $em = $this->getDoctrine()->getManager();

        $books = $em->getRepository('BooksBundle:Books')->findAll();


        return $this->render('books/backindex.html.twig', array(
            'books' => $books,
        ));
    }
    public function chartsAction()

    {
        $em = $this->getDoctrine()->getManager();

        $books = $em->getRepository('BooksBundle:Books')->findAll();


        return $this->render('books/chart.html.twig', array(
            'books' => $books,
        ));
    }
    public function indexAction()

    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.token_storage')->getToken()->getUser();


        $Likes= $em->getRepository('BooksBundle:Likes')->findBy(array("idetd"=>$user->getId()));
        $wishliste = $em->getRepository('BooksBundle:Wishliste')->findBy(array("idetd"=>$user->getId()));

        $books = $em->getRepository('BooksBundle:Books')->findAll();
        $bookscategorie = $em->getRepository('BooksBundle:Books')->findAll();

        return $this->render('books/index.html.twig', array(
            'books' => $books,'wishliste'=>$wishliste,'categorie'=>$bookscategorie,'Likes'=>$Likes,
        ));
    }
    public function filtrageAction($categoriebook){
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $Likes= $em->getRepository('BooksBundle:Likes')->findBy(array("idetd"=>$user->getId()));
        $wishliste = $em->getRepository('BooksBundle:Wishliste')->findBy(array("idetd"=>$user->getId()));
        $books = $em->getRepository('BooksBundle:Books')->findBy(array("categoriebook"=>$categoriebook));
        $bookscategorie = $em->getRepository('BooksBundle:Books')->findAll();
        return $this->render('books/index.html.twig', array(
            'books' => $books,'wishliste'=>$wishliste,'categorie'=>$bookscategorie,'Likes'=>$Likes,
        ));
    }

    /**
     * Creates a new book entity.
     *
     */
    public function newAction(Request $request)
    {
        $book = new Books();
        $form = $this->createForm('BooksBundle\Form\BooksType', $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $book->uploadProfilePicture();
            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute('books_backindex', array('idbook' => $book->getIdbook()));
        }


        return $this->render('books/new.html.twig', array(
            'book' => $book,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a book entity.
     *
     */
    public function showAction(Books $book)
    {
        $deleteForm = $this->createDeleteForm($book);



        return $this->render('books/show.html.twig', array(
            'book' => $book,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing book entity.
     *
     */
    public function editAction(Request $request, Books $book)
    {
        $deleteForm = $this->createDeleteForm($book);
        $editForm = $this->createForm('BooksBundle\Form\BooksType', $book);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('books_edit', array('idbook' => $book->getIdbook()));
        }

        return $this->render('books/edit.html.twig', array(
            'book' => $book,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a book entity.
     *
     */
    public function deleteAction(Request $request, Books $book)
    {
        $form = $this->createDeleteForm($book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($book);
            $em->flush();
        }

        return $this->redirectToRoute('books_index');
    }

    /**
     * Creates a form to delete a book entity.
     *
     * @param Books $book The book entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Books $book)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('books_delete', array('idbook' => $book->getIdbook())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    public function commentBookAction(Request $request,$idbook)
    {
        $id = $idbook;
        $em = $this->getDoctrine()->getManager();


        $books = $em->getRepository('BooksBundle:Books')->find($idbook);

        $thread = $this->container->get('fos_comment.manager.thread')->findThreadById($id);
        if (null === $thread) {
            $thread = $this->container->get('fos_comment.manager.thread')->createThread();
            $thread->setId($id);
            $thread->setPermalink($request->getUri());

            // Add the thread
            $this->container->get('fos_comment.manager.thread')->saveThread($thread);
        }

        $comments = $this->container->get('fos_comment.manager.comment')->findCommentTreeByThread($thread);

        return $this->render('books/show.html.twig', array(
            'comments' => $comments,
            'thread' => $thread,
            'book' => $books,
        ));
    }
}
